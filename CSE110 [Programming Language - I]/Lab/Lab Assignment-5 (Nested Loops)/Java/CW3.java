import java.util.Scanner;

public class CW3 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Start: ");
        int start = sc.nextInt();
        System.out.print("End: ");
        int end = sc.nextInt();
        System.out.println("Armstrong numbers:");
        for (int num = start; num <= end; num++) 
        {
            int temp = num;
            int digits = 0;
            while (temp != 0) 
            {
                temp /= 10;
                digits++;
            }
            temp = num;
            int sum = 0;
            while (temp != 0) 
            {
                int digit = temp % 10;
                int power = 1;

                for (int i = 1; i <= digits; i++) 
                {
                    power *= digit;    // sum += (int) Math.pow(digit, digits);
                }
                sum += power;
                temp /= 10;
            }
            if (sum == num) 
            {
                System.out.println(num);
            }
        }
    }
}