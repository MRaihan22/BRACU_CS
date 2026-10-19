import java.util.Scanner;

public class Task5 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Input: ");
        int num = sc.nextInt();
        int temp = num;
        int digits = 0;
        if (temp == 0) 
        {
            digits = 1;
        } 
        else {
            while (temp != 0) 
            {
                temp /=  10;
                digits++;
            }
        }

        int divisor = 1;
        for (int i = 1; i < digits; i++) 
        {
            divisor *= 10;
        }

        while (divisor >= 1) 
        {
            int digit = num / divisor;
            System.out.print(digit);
            if (divisor > 1) 
            {
                System.out.print(", ");
            }
            num = num % divisor; 
            divisor /= 10;
        }
        System.out.println();
    }
}