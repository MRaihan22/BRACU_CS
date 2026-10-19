import java.util.Scanner;

public class CW2 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("N = ");
        int n = sc.nextInt();
        int num = 2;
        int count = 0;

        while (count < n) 
        {
            int divisorCount = 0;
            for (int i = 1; i <= num; i++) 
            {
                if (num % i == 0)
                {
                    divisorCount++;
                }
            }
            if (divisorCount == 2) 
            {
                System.out.println(num);
                count++;
            }
            num++;
        }
    }
}